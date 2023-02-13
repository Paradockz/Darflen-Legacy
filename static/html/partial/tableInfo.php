<tr class="internal-table-section">
    <td class="internal-table-item">
        <?php echo $user['id'] ?>
    </td>
    <td class="internal-table-item">
        <?php echo $user['email'] ?>
    </td>
    <td class="internal-table-item">
        <?php echo $user_data['username'] ?>
    </td>
    <td class="internal-table-item">
        <?php echo time_ago($user_data['miscellaneous']['creation_time']) ?>
    </td>
    <td class="internal-table-item">
        <ul class="internal-table-list">
            <li>
                <a href="https://www.darflen.com/users/<?php echo $user['identifier'] ?>/ban">
                    <button class="lb-button internal-table-button">
                        <img src="https://static.darflen.com/img/icons/interface/ban.svg" alt="Ban icon">
                    </button>
                </a>
            </li>
            <li>
                <a href="https://www.darflen.com/users/<?php echo $user['identifier'] ?>/edit">
                    <button class="lb-button internal-table-button">
                        <img src="https://static.darflen.com/img/icons/interface/pen.svg" alt="Edit icon">
                    </button>
                </a>
            </li>
            <li>
                <a href="https://www.darflen.com/users/<?php echo $user['identifier'] ?>">
                    <button class="lb-button internal-table-button">
                        <img src="https://static.darflen.com/img/icons/interface/user.svg" alt="User icon">
                    </button>
                </a>
            </li>
        </ul>
    </td>
</tr>